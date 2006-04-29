<?php
/*

     NOTE: DO NOT RUN THIS THROUGH A WEB INTERFACE!

     Welcome to Joachim's Neural Network example for PHP

     A few days ago, I started working on a PHP IRC bot, and while thinking up
     cool features for the bot, I found the idea to use a neural network to
     make the bot be able to compose simple sentences. Neural networks are excellent
     for recognizing patterns and for predicting upcoming ones - I needed this to
     look for patterns in the text and to be able to respond based on the bot's training,
     rather than just use a bunch of if strpos(command, "how are you") respond("i am fine").

     If you have no previous experience with Neural networks, I suggest you read some basic
     descriptions and tutorials. Here is the resource I used to compose this script -
     Please remember that I was a complete beginner when I started writing this script. There
     may be errors and I still need to add momentum, memory banks, fuzzy logic, and jittering.
     http://cortex.snowseed.com/neural_networks.htm

     Here are some definitions:

     * -------- The Neuron --------
     * Takes a number of inputs
     * Multiplies each one by a 'weight'
     * Sums all inputs x weights
     * Applies an activation function to give an output.

     * ----- A Neural Network -----
     * Recognizes patterns and after training should be
     * able to give reasonable predictions as to what the output should be.

     * - A Backwards Propogation NN -
     * Working out how far wrong the output is in its current
     * state (the 'error'), and calculating a change in weights
     * backwards through the network to correct this error
     *     Output -> Hidden -> Input

*/

set_time_limit(0);
//This stops PHP from timing out on us

define("LEARNING_RATE",0.5);
//The learning rate is a measure of how much the weights are changed in each training cycle

class neuron {
//These are all different factors of each neuron
//Read up on Neural nets to find out what they mean

    var $bias;
    var $weights;
    var $output;
    var $delta;

    function neuron() {

        $bias = 0;
        $weights[1] = 0;
        $weights[2] = 0;
        $output = 0;
        $delta = 0;
    }
}

class nn {

    var $hl; //Two neurons in the hidden layer
    var $ol; //One output neuron

    /*
    We end up with the following structure:
    A three layer backpropagation network

    Input --- Hidden
            X         >  Output
    Input --- Hidden
    */

    function nn() {
    //Initializing the net

        $this->hl[1] = new neuron;
        $this->hl[2] = new neuron;
        $this->ol = new neuron;

        for($i=1; $i <= 2; $i++) {
            $this->hl[1]->weights[$i] = 0;
            $this->hl[2]->weights[$i] = 0;
            $this->ol->weights[$i] = 0;
        }
    }


    function train($input1, $input2, $target) {

        for($i=1; $i <= 2; $i++) {
            $this->hl[$i]->output = $this->activation($this->hl[$i]->bias + ($input1 * $this->hl[$i]->weights[1]) + ($input2 * $this->hl[$i]->weights[2]));
        }
        //Find the current output for the Hidden Layer Neurons:
        //Output = Activation(Bias + Input[n] * Weight[n])

        $this->ol->output = $this->activation($this->ol->bias + ($this->hl[1]->output * $this->ol->weights[1]) + ($this->hl[2]->output * $this->ol->weights[2]));
        $this->ol->delta = $this->ol->output * (1 - $this->ol->output) * ($target - $this->ol->output);

        //The output neuron takes as its input the output from the two hidden layer Neurons.
        //So for the output neuron weight(1) is the weight from HiddenNeuron(1),
        //and weight(2) is the weight for HiddenNeuron(2)

        /*
        Once we have the delta, it allows us to make an alteration to the
        weights in the network. The bigger the Delta, the larger the error
        in the network, and so the larger we want to alter the weights.
        This enables the network to become better after every training

        The above calculation of OutputNeuron->Delta first multiplies the
        output by (1- output). This has the effect of providing a larger
        figure when the output is at 0.5, and a minimum figure when the out
        put is at either 1 or 0 (do the math to confirm this). I.E. The Delta
        will be bigger, and so we're going to adjust the weight MORE when
        the current output is in the middle of the range (i.e. near 0.5). If
        the output is at either end of the range (i.e. at 1 or 0) then the
        Delta will come out smaller, and so we want to adjust the weight LESS.
        This simply has the effect of moving the weights more quickly if
        the current output from the Neuron is around 0.5 - the weight will
        be moved less if the neuron output is near 0 or near 1. (Bear in
        mind usually you'll want to get a more definite answer from
        a neural network - you want it to say 'Yesor 'No(i.e. 1 or 0)
        0.5 corresponds to 'Maybe', which isn't a very useful answer.

        This figure is then multiplied by (Target - OutputNeuron->Output)
        This has the effect of making the delta LARGER if the error of the
        Neuron is larger.

        So overall this math says 'The Delta will be larger the nearer the
        Neuron output is to 1 or 0, and it will be larger the more wrong
        the Neuron is'.
        */

        for($i=1; $i <= 2; $i++) {
            $this->hl[$i]->delta = $this->hl[$i]->output * (1 - $this->hl[$i]->output) * ($this->ol->weights[$i] * $this->ol->delta);
            $this->hl[$i]->bias = $this->hl[$i]->bias + (LEARNING_RATE * $this->hl[$i]->delta);
            $this->hl[$i]->weights[1] = $this->hl[$i]->weights[1] + (LEARNING_RATE * $input1 * $this->hl[$i]->delta);
            $this->hl[$i]->weights[2] = $this->hl[$i]->weights[2] + (LEARNING_RATE * $input2 * $this->hl[$i]->delta);
        }

        /*
        These deltas are the ones for the Hidden Layer. The math is similar
        here except for the last factor. Remember the Delta for each Neuron
        is how much we want to correct it by, but for the hidden layer, we
        don't have a specific figure of precisely what we want the output
        to be, so the Delta has to be calculated by how wrong the Output
        Neuron was (which is its Delta) and the current weight from the
        Hidden Neuron to the Output one. As far as I can see, the current
        weight is included as a factor here to reflect how 'importantthat
        current weight is - the more important it is - i.e. the more its
        going to affect the Output Neuron, the more it should be altered.

        So now we have the delta for each Neuron - how much we want to change
        each Neuron's weights. So we'll use them to update the weights.

        See above how the Weight is altered by the Delta multiplied by the
        Learning rate - the larger the delta, and the larger the learning
        rate (which is a constant) - the more we're going to change each
        weight. But - the important part here is that we alter the weight
        of the Neuron also in terms of the INPUT. The larger the input was
        the more important this weight is to alter and so the more we're
        going to alter it by. - Bear this in mind when you look at how
        the weights for two neurons can start moving in the same direction
        initially and then change to moving in opposite directions - this
        is because of the Delta mainly being applied to a weight when
        there is a high input on that weight.
        */


        $this->ol->bias = $this->ol->bias + (LEARNING_RATE * $this->ol->delta);
        $this->ol->weights[1] = $this->ol->weights[1] + (LEARNING_RATE * $this->hl[1]->output * $this->ol->delta);
        $this->ol->weights[2] = $this->ol->weights[2] + (LEARNING_RATE * $this->hl[2]->output * $this->ol->delta);

        //And the same for the output neuron
    }

    function activation($value) {

    //The activation function is used to give us a value between 0 and 1
        return (1 / (1 + exp($value * -1)));
    }

    function runnetwork($input1, $input2) {
    //This takes the activation function of the sum of all the inputs multiplied by their respective weights.

        for($i=1; $i <= 2; $i++) {
            $this->hl[$i]->output = $this->activation($this->hl[$i]->bias + ($this->hl[$i]->weights[1] * $input1) + ($this->hl[$i]->weights[2] * $input2));
        }
        $this->ol->output = $this->activation($this->ol->bias + ($this->ol->weights[1] * $this->hl[1]->output) + ($this->ol->weights[2] * $this->hl[2]->output));
        return $this->ol->output;
    }

}


//This creates an instance of our neural network class and trains it to output 1 when it receives 0 and 0
//This shows how you can create your own logic gates that operate using artificial intelligence
$neural = new nn;
for($i=1; $i <= 2000; $i++) {
    $neural->train(6, 6, 1);
    $neural->train(5, 8, 1);
}
print "Trained 2000 times to return 1 on 0, 0 (typical XOR logic)<br>";
print "The system recalls " . $neural->runnetwork(3,3) . " from memory!";

?>